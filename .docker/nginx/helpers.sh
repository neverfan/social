replace_in_config() {
    search_key="$1"
    new_value="$2"
    config="$3"
    sed -i "s/$search_key/$new_value/" "$config"
}

starts_with() {
    input_data="$1"
    starts_with_key="$2"
    [ $(echo "$input_data" | grep -E "^$starts_with_key") ]
    return $?
}

not_starts_with() {
    starts_with "$1" "$2"
    ret_code=$?
    if [ $ret_code -eq 0 ] ; then
        return 1;
    else
        return 0;
    fi;
}

replace_comma_to_spaces() {
    sed "s/,\|\t/ /g" | \
    sed "s/ \+/ /g" | \
    sed "s/^ \| $//g"
}

get_www_names(){
    env_variable=$1
    if starts_with "$env_variable" "ENV_NGINX_HOST=" ; then
        get_www_hosts=$(echo $env_variable| sed "s/ENV_NGINX_HOST=//g");
        echo "$get_www_hosts" | sed 's/,/\n/g' | \
        while read xline;
        do
            if starts_with "$xline" "www." ; then
                echo "$xline";
            else
                echo "www.$xline";
            fi;
        done
    fi;
}

get_host_names(){
    get_www_names "$1" | \
    while read www_host_name;
    do
        echo "$www_host_name" | sed 's/www\.//g'
    done

}

get_server_template(){
    result=$(get_server_template_raw "$1" |\
    sed 's/NL/\n/g' |\
    sed 's/DLR/\$/g')
    echo -e "$result"
}

get_server_template_raw(){
    new_name="$1"
    echo -e 'server {NL    listen 80;NL    server_name www.'$new_name';NL    return 302 DLRscheme://'$new_name'DLRrequest_uri;NL}NLNL'
}
